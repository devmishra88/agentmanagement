import { useQuery, useMutation, useQueryClient, queryCache } from "react-query";
import { secureRequest, objectToFormData } from "../utils/axios-utils";

const addEditNewspaper = (data) => {
  return secureRequest({ url: `/newspaper.php`, method: `POST`, data: data });
};

const fetchNewspapers = () => {
  const reqObject = { Mode: `GetAllNewspaper` };
  const formData = objectToFormData(reqObject);
  return secureRequest({ url: `/newspaper.php`, method: `POST`, data: formData });
};

const fetchSingleNewspaper = ({ queryKey }) => {
  const newspaperId = queryKey[1];
  const reqObject = { Mode: `GetNewspaperDetail`, recordid: newspaperId };
  const formData = objectToFormData(reqObject);
  return secureRequest({ url: `/newspaper.php`, method: `POST`, data: formData });
};

export const deleteNewspaperById = async (newspaperId) => {
  try {
    const response = await secureRequest({
      url: `/newspaper.php`,
      method: 'DELETE',
      data: { recordid:newspaperId },
    });

    return response.data;
  } catch (error) {
    console.error('Error deleting Newspaper:', error);
    throw error;
  }
};

export const useAddNewspaperData = () => {
  const queryClient = useQueryClient();

  return useMutation(addEditNewspaper, {
    onMutate: async (newNewspaper) => {
      await queryClient.cancelQueries(`newspapers`);
      const previousNewspaperData = queryClient.getQueryData(`newspapers`);

      queryClient.setQueriesData(`newspapers`, (oldQueryData) => {
        return {
          ...oldQueryData,
          data: [
            ...oldQueryData.data,
            { id: oldQueryData?.data?.length + 1, ...newNewspaper },
          ],
        };
      });
      return {
        previousNewspaperData,
      };
    },
    onSuccess: (data) => {
      queryClient.setQueryData(`newspapers`, data);
    },
    onError: (_error, _newspaper, context) => {
      queryClient.setQueryData(`newspapers`, context.previousNewspaperData);
    },
    onSettled: () => {
      queryClient.invalidateQueries(`newspapers`);
    },
  });
};

export const useNewspapersData = (onSuccess, onError) => {

  const queryClient = useQueryClient();

  const { data, ...rest } = useQuery(`newspapers`, fetchNewspapers, {
    onSuccess,
    onError,
  });

  const mutateDeleteNewspaper = useMutation(
    (id) => deleteNewspaperById(id),
    {
      onSuccess: (response, id) => {

        const tempdata = data?.data

        queryClient.invalidateQueries('newspapers', { refetchActive: true });

        const updatedNewspapers = data? tempdata?.recordlist.filter(newspaper => newspaper.id !== id) : [];

        queryClient.setQueryData('newspapers', {
          success: true,
          msg: 'Newspaper listed successfully.',
          recordlist: updatedNewspapers,
          totalrecord: updatedNewspapers.length,
        });
      },
    }
  );

  const deleteNewspaper = (id) => {
    mutateDeleteNewspaper.mutate(id);
  };

  return { data, deleteNewspaper, ...rest };
};

export const useSingleNewspaperData = (newspaperId) => {
  const queryClient = useQueryClient();

  return useQuery(["newspaper", newspaperId], fetchSingleNewspaper, {
    initialData: () => {
      queryClient.prefetchQuery(["newspaper", newspaperId], fetchSingleNewspaper);

      const prefetchedData = queryClient.getQueryData(["newspaper", newspaperId]);

      if (prefetchedData?.data?.success) {
        return prefetchedData;
      } else {
        return undefined;
      }
    },
  });
};
