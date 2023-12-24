import { useQuery, useMutation, useQueryClient, queryCache } from "react-query";
import { secureRequest, objectToFormData } from "../utils/axios-utils";

const addEditArea = (areadata) => {
  return secureRequest({ url: `/area.php`, method: `POST`, data: areadata });
};

const fetchAreas = () => {
  const reqObject = { Mode: `GetAllArea` };
  const formData = objectToFormData(reqObject);
  return secureRequest({ url: `/area.php`, method: `POST`, data: formData });
};

const fetchSingleArea = ({ queryKey }) => {
  const areaId = queryKey[1];
  const reqObject = { Mode: `GetAreaDetail`, recordid: areaId };
  const formData = objectToFormData(reqObject);
  return secureRequest({ url: `/area.php`, method: `POST`, data: formData });
};

export const deleteAreaById = async (areaId) => {
  try {
    const response = await secureRequest({
      url: `/area.php`,
      method: 'DELETE',
      data: { recordid:areaId },
    });

    return response.data;
  } catch (error) {
    console.error('Error deleting area:', error);
    throw error;
  }
};

export const useAddAreaData = () => {
  const queryClient = useQueryClient();

  return useMutation(addEditArea, {
    onMutate: async (newArea) => {
      await queryClient.cancelQueries(`areas`);
      const previousAreaData = queryClient.getQueryData(`areas`);

      queryClient.setQueriesData(`areas`, (oldQueryData) => {
        return {
          ...oldQueryData,
          data: [
            ...oldQueryData.data,
            { id: oldQueryData?.data?.length + 1, ...newArea },
          ],
        };
      });
      return {
        previousAreaData,
      };
    },
    onError: (_error, _area, context) => {
      queryClient.setQueryData(`areas`, context.previousAreaData);
    },
    onSettled: () => {
      queryClient.invalidateQueries(`areas`);
    },
  });
};

export const useAreasData = (onSuccess, onError) => {

  const queryClient = useQueryClient();

  const { data, ...rest } = useQuery(`areas`, fetchAreas, {
    onSuccess,
    onError,
  });

  const mutateDeleteArea = useMutation(
    (id) => deleteAreaById(id),
    {
      onSuccess: (response, id) => {

        const tempdata = data?.data

        queryClient.invalidateQueries('areas', { refetchActive: true });

        const updatedAreas = data? tempdata?.recordlist.filter(area => area.id !== id) : [];

        queryClient.setQueryData('areas', {
          success: true,
          msg: 'Area listed successfully.',
          recordlist: updatedAreas,
          totalrecord: updatedAreas.length,
        });
      },
    }
  );

  const deleteArea = (id) => {
    mutateDeleteArea.mutate(id);
  };

  return { data, deleteArea, ...rest };
};

export const useSingleAreaData = (areaId) => {
  const queryClient = useQueryClient();

  return useQuery(["area", areaId], fetchSingleArea, {
    initialData: () => {
      queryClient.prefetchQuery(["area", areaId], fetchSingleArea);

      const prefetchedData = queryClient.getQueryData(["area", areaId]);

      if (prefetchedData?.data?.success) {
        return prefetchedData;
      } else {
        return undefined;
      }
    },
  });
};
